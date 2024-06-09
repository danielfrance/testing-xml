import blog1 from '../../../../images/blog/blog-01.jpg';
import blog2 from '../../../../images/blog/blog-02.jpg';
import blog3 from '../../../../images/blog/blog-03.jpg';



export default function Blog() {
    return (
        <section className="bg-white pb-10 pt-20 dark:bg-dark lg:pb-20 lg:pt-[120px]">
            <div className="container mx-auto">
                <div className="-mx-4 flex flex-wrap justify-center">
                    <div className="w-full px-4">
                        <div className="mx-auto mb-[60px] max-w-[485px] text-center">
                            <span className="mb-2 block text-lg font-semibold text-primary">
                                Our Blogs
                            </span>
                            <h2
                                className="mb-4 text-3xl font-bold text-dark dark:text-white sm:text-4xl md:text-[40px] md:leading-[1.2]"
                            >
                                Our Recent News
                            </h2>
                            <p className="text-base text-body-color dark:text-dark-6">
                                There are many variations of passages of Lorem Ipsum available
                                but the majority have suffered alteration in some form.
                            </p>
                        </div>
                    </div>
                </div>
                <div className="-mx-4 flex flex-wrap">
                    <div className="w-full px-4 md:w-1/2 lg:w-1/3">
                        <div className="wow fadeInUp group mb-10" data-wow-delay=".1s">
                            <div className="mb-8 overflow-hidden rounded-[5px]">
                                <a href="blog-details.html" className="block">
                                    <img
                                        src={blog1}
                                        alt="image"
                                        className="w-full transition group-hover:rotate-6 group-hover:scale-125"
                                    />
                                </a>
                            </div>
                            <div>
                                <span
                                    className="mb-6 inline-block rounded-[5px] bg-primary px-4 py-0.5 text-center text-xs font-medium leading-loose text-white"
                                >
                                    Dec 22, 2023
                                </span>
                                <h3>
                                    <a
                                        href=""
                                        className="mb-4 inline-block text-xl font-semibold text-dark hover:text-primary dark:text-white dark:hover:text-primary sm:text-2xl lg:text-xl xl:text-2xl"
                                    >
                                        Meet AutoManage, the best AI management tools
                                    </a>
                                </h3>
                                <p
                                    className="max-w-[370px] text-base text-body-color dark:text-dark-6"
                                >
                                    Lorem Ipsum is simply dummy text of the printing and
                                    typesetting industry.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="w-full px-4 md:w-1/2 lg:w-1/3">
                        <div className="wow fadeInUp group mb-10" data-wow-delay=".15s">
                            <div className="mb-8 overflow-hidden rounded-[5px]">
                                <a href="blog-details.html" className="block">
                                    <img
                                        src={blog2}
                                        alt="image"
                                        className="w-full transition group-hover:rotate-6 group-hover:scale-125"
                                    />
                                </a>
                            </div>
                            <div>
                                <span
                                    className="mb-6 inline-block rounded-[5px] bg-primary px-4 py-0.5 text-center text-xs font-medium leading-loose text-white"
                                >
                                    Mar 15, 2023
                                </span>
                                <h3>
                                    <a
                                        href=""
                                        className="mb-4 inline-block text-xl font-semibold text-dark hover:text-primary dark:text-white dark:hover:text-primary sm:text-2xl lg:text-xl xl:text-2xl"
                                    >
                                        How to earn more money as a wellness coach
                                    </a>
                                </h3>
                                <p
                                    className="max-w-[370px] text-base text-body-color dark:text-dark-6"
                                >
                                    Lorem Ipsum is simply dummy text of the printing and
                                    typesetting industry.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="w-full px-4 md:w-1/2 lg:w-1/3">
                        <div className="wow fadeInUp group mb-10" data-wow-delay=".2s">
                            <div className="mb-8 overflow-hidden rounded-[5px]">
                                <a href="blog-details.html" className="block">
                                    <img
                                        src={blog3}
                                        alt="image"
                                        className="w-full transition group-hover:rotate-6 group-hover:scale-125"
                                    />
                                </a>
                            </div>
                            <div>
                                <span
                                    className="mb-6 inline-block rounded-[5px] bg-primary px-4 py-0.5 text-center text-xs font-medium leading-loose text-white"
                                >
                                    Jan 05, 2023
                                </span>
                                <h3>
                                    <a
                                        href=""
                                        className="mb-4 inline-block text-xl font-semibold text-dark hover:text-primary dark:text-white dark:hover:text-primary sm:text-2xl lg:text-xl xl:text-2xl"
                                    >
                                        The no-fuss guide to upselling and cross selling
                                    </a>
                                </h3>
                                <p
                                    className="max-w-[370px] text-base text-body-color dark:text-dark-6"
                                >
                                    Lorem Ipsum is simply dummy text of the printing and
                                    typesetting industry.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    )
}